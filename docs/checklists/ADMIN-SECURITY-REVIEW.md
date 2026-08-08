# Checklist — Revue de sécurité de l'interface d'administration éditoriale

Cette checklist s'applique à toute l'interface `/admin/**` (Phases 8B, 8C1,
8C2, 8C3, 8C4). Elle est **complémentaire** de `PRODUCTION-CONTACT.md`
et couvre les surfaces éditoriales.

Il s'agit d'une **revue de sécurité ciblée inspirée des contrôles OWASP ASVS
applicables à la surface d'administration Devzair** — les items listés
pointent vers des chapitres ASVS lorsque pertinent, mais **ce document ne
constitue pas une certification ASVS complète** : la grille officielle
n'a pas été auditée dans son intégralité. Les risques évoqués correspondent
également aux catégories **OWASP Top 10 2021** applicables (A01 Broken Access
Control, A03 Injection, A05 Security Misconfiguration, A07 Identification and
Authentication Failures). Chaque item est vérifiable : soit par un test
automatique déjà présent dans la suite, soit par une commande locale
reproductible. Les références `ASVS X.Y` pointent vers les chapitres
correspondants de l'ASVS pour faciliter la traçabilité.

Convention : chaque case doit être cochée par la personne qui a effectué la
revue, avec la date, la commande exécutée et le résultat. Ne jamais cocher
pour quelqu'un d'autre. En cas de doute, ouvrir une entrée dans
`docs/10-TRACKING.md` plutôt que de valider silencieusement.

---

## 1. Authentification (ASVS 2 — Authentication)

- [ ] La firewall `admin` (`config/packages/security.yaml`) intercepte
      **toutes** les URL sous `/admin/**` sauf `/admin/login`.
      Vérification : `grep -n 'pattern:' apps/api/config/packages/security.yaml`.
- [ ] `access_control` refuse tout accès anonyme à `/admin/**` (ROLE_ADMIN
      obligatoire). Test : `tests/Admin/Presentation/Http/*` — chaque
      contrôleur a un `testAnonymousUserIsRedirectedToLogin`.
- [ ] Les mots de passe sont hashés avec l'algo par défaut Symfony
      (`auto`, actuellement bcrypt / argon2id). **Jamais** de MD5/SHA1.
      Vérification : `grep -n 'algorithm' apps/api/config/packages/security.yaml`.
- [ ] Aucun mot de passe n'apparaît en clair dans les logs ni dans les
      URL. Les commandes CLI `app:admin:create-user` et le login
      Playwright utilisent `--password-stdin` (aucune fuite via `ps`).
- [ ] Le compte E2E `e2e-admin@example.test` utilise un domaine RFC 6761
      réservé (`.test`), impossible à confondre avec un compte réel.

## 2. Contrôle d'accès (ASVS 4 — Access Control)

- [ ] Chaque contrôleur admin porte `#[IsGranted('ROLE_ADMIN')]` en plus
      du firewall. Vérification par introspection : chaque contrôleur
      dans `src/Admin/Presentation/Http/` doit avoir l'attribut au niveau
      classe **ou** au niveau méthode.
- [ ] Aucune route publique (`config/routes/*.yaml` hors `admin.yaml`)
      n'expose de méthode d'écriture éditoriale. Vérification :
      `grep -rn 'methods:' apps/api/config/routes/ | grep -v admin.yaml`
      et confirmer qu'aucune ne combine `POST`/`PUT`/`DELETE` sur des
      chemins éditoriaux publics.
- [ ] Le port `AdminArticleReadRepositoryInterface` n'expose aucune méthode
      d'écriture (introspection réflexive : voir
      `tests/Editorial/Application/Query/GetAdminArticlePreviewHandlerTest::testPortInterfaceHasNoWriteMethodsExposedToHandler`).
- [ ] La route de prévisualisation `/admin/articles/{id}/preview` est
      déclarée `methods: [GET]` : un POST doit renvoyer 405.
      Test : `AdminArticlePreviewControllerTest::testPreviewIsGetOnlyAndPostReturnsMethodNotAllowed`.

## 3. Session et cookies (ASVS 3 — Session Management)

- [ ] `session.cookie_secure = auto` (déjà par défaut chez Symfony).
- [ ] `session.cookie_httponly = true`.
- [ ] `session.cookie_samesite = lax` (défaut Symfony 7.4).
      Vérification : `grep -rn 'samesite\|httponly\|cookie_secure' apps/api/config/`.
- [ ] La session expire à la déconnexion et un nouveau `Set-Cookie` est
      émis à la reconnexion (Symfony fait cela nativement via
      `session_regenerate_id()` en cas de changement de rôle).

## 4. Protection CSRF (ASVS 4.2)

- [ ] Toutes les routes admin qui **mutent** l'état demandent un jeton
      CSRF vérifié côté serveur (`isCsrfTokenValid` ou binding du form).
      Points d'entrée : `admin_article_publish`, `admin_article_archive`,
      `admin_article_restore`, `admin_article_edit` (POST),
      `admin_article_create` (POST).
- [ ] Les jetons sont **par-action** (`article_publish_{id}`,
      `article_archive_{id}`, `article_restore_{id}`), jamais partagés
      entre actions.
- [ ] La page de prévisualisation réutilise ces jetons existants — aucune
      nouvelle surface CSRF introduite. Test :
      `AdminArticlePreviewControllerTest::testActionsCarryValidCsrfTokens`.

## 5. Injection & sanitisation (ASVS 5 — Validation, Sanitization, Encoding)

- [ ] Twig est configuré avec `autoescape='html'` (défaut). Aucun `|raw`
      dans les templates admin sauf **un seul** : `preview.html.twig`
      sur `preview.contentHtml`. Vérification :
      `grep -rn '|raw' apps/api/templates/admin/`.
- [ ] `preview.contentHtml` provient exclusivement de
      `CommonMarkArticleRenderer` avec `MarkdownSecurityPolicy` :
      `html_input=strip`, `allow_unsafe_links=false`, `max_nesting_level`,
      `max_delimiters_per_line`. Test :
      `GetAdminArticlePreviewHandlerTest::testRawHtmlInMarkdownIsStrippedNotRendered`
      et `::testDangerousLinksAreNeutralized`.
- [ ] Les autres champs (titre, slug, chapô, SEO, auteur) sont rendus
      par Twig avec l'escape par défaut. Test :
      `AdminArticlePreviewControllerTest::testPreviewEscapesUserProvidedTitleAndSlug`.
- [ ] Aucun `SELECT` ou `DELETE` avec concaténation de chaîne dans le
      code applicatif. Toutes les requêtes utilisent DQL ou le
      QueryBuilder Doctrine. Vérification :
      `grep -rn '\$this->getEntityManager()->getConnection()->exec' apps/api/src/`.
- [ ] Les scripts de fixture E2E utilisent des préfixes codés en dur
      (`e2e-8c3-`, `e2e-8c4-preview-`) et jamais interpolent une variable
      d'environnement dans le `LIKE`. Vérification :
      `grep -n 'E2E_SLUG_PREFIX' scripts/e2e-admin-*.sh`.

## 6. En-têtes de sécurité HTTP (ASVS 14.4)

Toutes les réponses `/admin/**` doivent porter les en-têtes suivants,
posés par `AdminSecurityHeadersSubscriber` :

- [ ] `Content-Security-Policy: default-src 'none'; …` (aucun script
      externe, `style-src 'self'` uniquement).
- [ ] `X-Frame-Options: DENY` (aucun iframe).
- [ ] `X-Content-Type-Options: nosniff`.
- [ ] `Referrer-Policy: no-referrer` (aucune fuite de l'URL admin vers
      un tiers).
- [ ] `X-Robots-Tag: noindex, nofollow` (interdit toute indexation).
- [ ] `Cache-Control: private, no-store, no-cache, must-revalidate`
      (aucune mise en cache navigateur / CDN d'une page authentifiée).

Test : `AdminArticlePreviewControllerTest::testSecurityHeadersAreAppliedToPreview`
+ suite Playwright `admin-preview.spec.ts` — test « en-têtes de sécurité
admin appliqués à la preview ».

## 7. Robots & indexation (ASVS 14.3)

- [ ] Le `robots.txt` public (`apps/web/server/routes/robots.txt.get.ts`
      + `nitro.config.ts`) interdit `/admin` explicitement.
- [ ] Le `sitemap.xml` public **n'expose jamais** d'URL `/admin/**`.
      Vérification : `curl -fsS http://localhost:3001/sitemap.xml | grep -i admin`
      doit ne rien renvoyer.
- [ ] L'en-tête `X-Robots-Tag: noindex, nofollow` est présent sur
      **toutes** les réponses `/admin/**` (voir §6). Défense en profondeur
      si un robot ignore `robots.txt`.

## 8. Isolation base de données (ASVS 5.3)

- [ ] Les fixtures E2E ciblent **exclusivement** des slugs préfixés
      (`e2e-8b2-*`, `e2e-8c3-*`, `e2e-8c4-preview-*`). Aucun `TRUNCATE`,
      aucun `DELETE` sans clause `WHERE slug LIKE 'e2e-…-%'`.
- [ ] Les scripts échouent explicitement (`set -euo pipefail`,
      `ON_ERROR_STOP=1`) — aucun `|| true` ne masque une erreur SQL.
- [ ] Le `clear` post-suite est en `if: always()` dans le workflow CI —
      aucun résidu même après échec Playwright.

## 9. Logs et audit (ASVS 7 — Error Handling and Logging)

- [ ] Aucun mot de passe, aucun jeton CSRF, aucun contenu privé
      (chapô/corps d'article) n'apparaît dans les logs applicatifs.
      Vérification manuelle : provoquer un login, une prévisualisation
      et une publication ; grep `docker compose logs api | grep -iE
      'password|csrf|_token'` ne doit retourner que des mentions
      structurelles (jamais de valeur en clair).
- [ ] En cas de 404 sur `/admin/articles/{id}/preview`, aucune fuite
      d'information sur l'existence ou non de l'article dans le
      corps de la réponse. Symfony renvoie la page 404 standard.

## 10. Non-mutation des lectures (invariant du domaine)

- [ ] Le handler `GetAdminArticlePreviewHandler` n'invoque aucune
      méthode de mutation sur l'article. Test :
      `GetAdminArticlePreviewHandlerTest::testHandlerDoesNotMutateArticle`.
- [ ] Aucun appel à `EntityManager::flush()` dans le chemin de
      prévisualisation. Vérification :
      `grep -n 'flush' apps/api/src/Admin/Presentation/Http/AdminArticlePreviewController.php
      apps/api/src/Editorial/Application/Query/GetAdminArticlePreviewHandler.php`
      doit ne rien retourner.
- [ ] Le pipeline `GET /admin/articles/{id}/preview` ne fait qu'une
      lecture DB (`findForEdit`) + un rendu Markdown. Vérification :
      instrumenter la requête avec le profiler Symfony et vérifier
      que le nombre de requêtes DB est ≤ 1.

## 11. Vérifications de fin de recette

- [ ] Toute la suite backend (`composer test`) passe 3 fois d'affilée
      avec 3 seeds différents (l'ordre des tests ne doit rien changer).
- [ ] La suite Playwright `admin-preview.spec.ts` passe 2 fois d'affilée
      (les scripts `load` en tête et `clear` en queue absorbent tout
      résidu).
- [ ] `docker compose logs api caddy web` après recette ne contient
      **aucune** ligne `ERROR` ou `CRITICAL` provoquée par les tests.

---

## Références

- **OWASP ASVS v4.0** — https://owasp.org/www-project-application-security-verification-standard/
- **OWASP Top 10 2021** — A01 Broken Access Control, A03 Injection,
  A05 Security Misconfiguration, A07 Identification and Authentication
  Failures.
- **RFC 6761 §6.4** — TLD `.test` réservé aux tests, jamais routable.
- **ADR-010** — Politique Markdown & sanitisation (`docs/adr/`).
- **ADR-011** — En-têtes de sécurité admin (`docs/adr/`).
