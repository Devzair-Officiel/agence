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
| `.github/workflows/api-quality.yml`   | `apps/api/**`                      | PHP 8.4 — `composer validate --strict` + `lint:yaml config` + `lint:container` + PHPUnit |

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
