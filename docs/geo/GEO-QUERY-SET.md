# GEO Query Set — Devzair

Phase 10B, DEC-096. Ce document définit un jeu de requêtes stable
utilisé pour observer la présence de Devzair dans les surfaces de
recherche génératives (ChatGPT Search, Claude Search, Perplexity,
Google AI overviews) et pour instrumenter, à l'avenir, des mesures
comparables au fil des mises à jour du site.

## Portée

- 12 requêtes actuelles, réparties en trois familles.
- **Aucune** requête ne mentionne une ville, un département ou une
  région tant que l'éligibilité géographique n'est pas validée
  (DEC-097 — Local eligibility UNDETERMINED).
- **Aucun** résultat attendu n'est renseigné. Les colonnes de résultat
  sont laissées vides et seront alimentées uniquement à partir
  d'observations réelles, avec date de relevé.

## Convention de nommage

Chaque requête possède un identifiant stable `QS-<famille>-<n>` qui
sert de clé lors des relevés. Ne pas renuméroter en cas d'ajout
ultérieur — ajouter à la suite.

## Famille A — Marque

| ID | Requête |
| --- | --- |
| QS-A-01 | agence Devzair |
| QS-A-02 | qui est Devzair agence digitale |
| QS-A-03 | Devzair services |

## Famille B — Expertises

| ID | Requête |
| --- | --- |
| QS-B-01 | agence digitale à taille humaine |
| QS-B-02 | comment créer un site internet professionnel |
| QS-B-03 | site vitrine ou site sur mesure comment choisir |
| QS-B-04 | maintenance site internet contrat |
| QS-B-05 | application métier remplacer Excel |
| QS-B-06 | SEO création site internet |

## Famille C — Problèmes prospects

| ID | Requête |
| --- | --- |
| QS-C-01 | mon site ne remonte plus sur Google que faire |
| QS-C-02 | comment améliorer la visibilité de mon entreprise en ligne |
| QS-C-03 | refonte de site quand décider |

## Requêtes explicitement exclues à ce stade

- Requêtes géographiques (« agence Paris », « site Lyon », etc.) :
  éligibilité locale non validée.
- Requêtes citant un nom de client : aucun client public n'a été
  validé pour communication (règle 1 AGENTS.md).
- Requêtes citant un chiffre d'affaires, un effectif ou une
  certification : aucune donnée validée.

## Processus de revue

- Fréquence cible : une revue par trimestre au démarrage.
- Chaque revue produit un rapport daté à part, jamais dans ce fichier.
- Le fichier lui-même n'évolue que sur ajout/retrait de requêtes,
  jamais sur mise à jour de résultats.
