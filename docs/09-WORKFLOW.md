# Méthode de travail et définition de terminé

> Contrôles obligatoires, prompts de travail et prochaine étape.

## 22. Définition de terminé

Une tâche est `TERMINÉE` uniquement si :

- le besoin est satisfait ;
- le code est relu ;
- les tests passent ;
- les erreurs sont gérées ;
- la sécurité est vérifiée ;
- le responsive est vérifié ;
- l’accessibilité concernée est vérifiée ;
- le SEO concerné est vérifié ;
- les données personnelles sont traitées conformément au plan ;
- la documentation est à jour ;
- aucun placeholder ou contenu fictif n’est exposé ;
- le changement est déployable et réversible ;
- ce fichier est mis à jour.

---

## 23. Protocole d’utilisation avec Codex

### Prompt de début de tâche

```text
Lis intégralement DEVZAIR_REFERENTIEL_PROJET.md.

Travaille uniquement sur la tâche suivante :
[DESCRIPTION PRÉCISE]

Avant de modifier le code :
1. inspecte l’existant ;
2. indique la phase concernée ;
3. liste les fichiers susceptibles d’être modifiés ;
4. identifie les risques SEO, sécurité, données personnelles, accessibilité et régression ;
5. propose un plan court.

Pendant l’implémentation :
- respecte la pile et les conventions existantes ;
- n’invente aucun contenu factuel ;
- ajoute ou adapte les tests ;
- ne modifie pas un périmètre sans rapport ;
- conserve la compatibilité avec les décisions ADR.

À la fin :
- exécute les contrôles disponibles ;
- résume les modifications ;
- indique les tests exécutés et leurs résultats ;
- mentionne les limites ou contrôles manuels restants ;
- mets à jour les cases et le journal du référentiel.
```

### Prompt de revue

```text
Analyse les changements de la branche par rapport au référentiel Devzair.

Recherche en priorité :
- régressions fonctionnelles ;
- failles de sécurité ;
- problèmes d’autorisation ;
- fuite de secrets ou données personnelles ;
- défauts SEO/indexation ;
- données structurées incorrectes ;
- accessibilité ;
- performance ;
- contenu inventé ou non prouvé ;
- tests manquants.

Classe les constats par criticité et cite les fichiers et lignes.
Ne propose pas de changement hors périmètre sans justification.
```

### Prompt de recette avant production

```text
Effectue une revue de préparation à la production à partir de
DEVZAIR_REFERENTIEL_PROJET.md.

Vérifie au minimum :
- build et tests ;
- variables et secrets ;
- HTTPS et redirections ;
- indexabilité ;
- robots.txt et sitemap ;
- canonicals ;
- pages légales et consentement ;
- formulaires et e-mails ;
- sécurité des en-têtes ;
- erreurs et logs ;
- accessibilité des parcours critiques ;
- performance ;
- sauvegarde et rollback.

Retourne :
1. GO ;
2. GO CONDITIONNEL avec conditions bloquantes ;
3. NO-GO avec causes précises.
```

---

## 28. Prochaine étape immédiate

La prochaine étape active est définie dans `docs/08-ROADMAP.md`. Consulter ce fichier avant toute intervention.

Phases en cours ou à venir au moment de la dernière mise à jour de ce document (2026-09-04) :

- Phase 10A2 — Maillage éditorial et découverte des ressources (EN COURS).
- Phase 11C — Accessibilité, performance et images (À VENIR).
- Phase 12 — Recette VPS (infrastructure Docker terminée, recette non démarrée).
- Programme Estimateur EST-2 (validation visuelle requise) et suivants.
- Chantier SEO-COM (SEO-COM-0 terminé, SEO-COM-1 à SEO-COM-10 à planifier).

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
