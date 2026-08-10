# GEO Measurement — Devzair

Phase 10B, DEC-096 + DEC-101. Ce document décrit **le plan** de mesure
de la présence de Devzair dans les surfaces génératives et de ses
référents IA. Aucun tracker, aucun endpoint analytics supplémentaire
n'est branché à ce stade.

État courant :

```
AI referral implementation : DEFERRED
Measurement plan            : DOCUMENTED
```

Note DEC-101 — La visibilité sur les surfaces génératives Google Search
(AI Overviews, AI Mode, snippets IA de Search) reste mesurable
indépendamment de la politique `Google-Extended: Disallow /`. Ces
surfaces exploitent l'index Google Search et Googlebot, pas le token
`Google-Extended` qui contrôle l'entraînement Gemini et le grounding
Gemini Apps / Vertex AI. Aucune métrique Gemini spécifique n'est
inventée : la revue trimestrielle continue de consigner les surfaces
observables réellement (Google standard + AI Overviews) sans postuler
un canal Gemini distinct tant qu'aucun référent identifiable ne
l'atteste.

## Objectifs

- Suivre dans le temps la présence de Devzair dans les surfaces IA
  publiques pour les requêtes du query set (voir
  `GEO-QUERY-SET.md`).
- Identifier les référents IA arrivant sur le site à partir des
  en-têtes `Referer` normalisés fournis par ChatGPT, Perplexity, Claude
  et Google AI overviews lorsque disponibles.
- Fournir une base répétable pour comparer deux revues consécutives.

Ce n'est pas :

- une plateforme d'analytics maison ;
- une collecte de contenu utilisateur ;
- une surveillance en continu.

## Query set review process

- Une **revue trimestrielle** couvre les 12 requêtes du query set,
  exécutées manuellement sur ChatGPT Search, Claude, Perplexity et
  Google (mode recherche standard + résumés IA lorsqu'ils apparaissent).
- Pour chaque requête, l'opérateur note :
  - la date et l'heure UTC ;
  - la surface (ChatGPT / Claude / Perplexity / Google) ;
  - la présence (`YES` / `NO` / `PARTIAL`) d'une mention de Devzair
    dans la réponse générative ;
  - la présence (`YES` / `NO`) d'un lien vers `devzair.fr` ou une
    sous-URL ;
  - la citation textuelle du passage citant Devzair, si applicable.
- Le résultat est archivé dans `docs/geo/observations/YYYY-MM-DD.md`
  (créé au premier relevé — pas en avance).
- **Aucun résultat n'est inventé**. Une case laissée vide est un
  résultat valable.

## AI referral tracking plan

### Signaux exploitables

- En-tête HTTP `Referer` porté par les navigateurs quand un utilisateur
  clique depuis une réponse générative.
- Certaines surfaces génératives (ChatGPT Search, Perplexity, Google)
  exposent un `Referer` identifiable ; d'autres (Claude, apps mobiles
  natives) le tronquent ou l'omettent.
- La liste précise des hôtes de `Referer` observables évolue et
  doit être consolidée à partir d'observations réelles, pas d'un
  répertoire présumé.

### Limitations

- Le `Referer` est absent quand la navigation part d'un `noreferrer`,
  d'une application native ou d'une réponse copiée-collée en dehors du
  navigateur.
- Les résumés IA (Google AI Overviews, snippets ChatGPT) peuvent citer
  Devzair sans générer de clic.
- Aucun de ces signaux ne permet d'attribuer précisément une
  conversion à une surface IA.
- Ce que nous mesurons est un **indicateur d'activité**, pas une
  attribution.

### Contraintes de confidentialité

- Aucune PII n'est stockée en lien avec un référent IA.
- L'analyse s'appuie sur les logs serveur agrégés déjà en place
  (X-Forwarded-For anonymisé côté Caddy en Phase 12), pas sur des
  identifiants persistants supplémentaires.
- Pas de cookie tiers, pas de fingerprinting, pas de tag manager
  supplémentaire pour cette mesure.
- Aucun outil d'analytics n'est activé au terme de Phase 10B ; la
  décision viendra avec Phase 12 / conformité RGPD.

## Modèle d'observation (à copier lors du premier relevé)

```
# Observation Devzair — <date UTC>
Opérateur : <initiales>
Environnement : ChatGPT Search / Claude / Perplexity / Google

## Résultats

| ID       | Surface | Devzair mentionné | Lien devzair.fr | Extrait éventuel |
| -------- | ------- | ----------------- | --------------- | ---------------- |
| QS-A-01  |         |                   |                 |                  |
| QS-A-02  |         |                   |                 |                  |
| …        |         |                   |                 |                  |

## Anomalies

- (Noter toute réponse incohérente ou contenu attribué à Devzair
  mais erroné — ex. adresse fictive, service non offert.)

## Actions déclenchées

- (Éventuelle correction de contenu ou balisage suite à une anomalie.)
```

## Réévaluation

- Le plan doit être revu si un nouveau moteur générant du trafic
  identifiable apparaît (par exemple un moteur avec API référencée
  côté Google Search Console).
- Le passage de `DEFERRED` à `IMPLEMENTED` demande une décision
  distincte (numéro DEC dédié) — cf. Phase 12.
