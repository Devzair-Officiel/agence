/**
 * Formatage éditorial des dates (jour + mois long + année) en français.
 *
 * Motif : les composants Vue rendus en SSR (Docker/Nitro, TZ système `UTC`)
 * puis hydratés côté client (navigateur en `Europe/Paris`) produisaient des
 * chaînes différentes pour un même ISO à cheval sur minuit UTC.
 * Exemple : `"2026-08-09T22:19:20+00:00"` → « 9 août 2026 » côté serveur,
 * « 10 août 2026 » côté client → Vue lève « Hydration text content
 * mismatch » et remplace le nœud texte en catastrophe.
 *
 * La fonction fixe donc explicitement `timeZone: "Europe/Paris"` : la même
 * date est produite quel que soit le TZ système du process (déterminisme
 * SSR ↔ client). Ce n'est pas une convention arbitraire : Devzair sert un
 * public francophone métropolitain, et les auteurs éditoriaux réfléchissent
 * en heure locale française — c'est la référence attendue par le lecteur.
 *
 * Contrat :
 *   - entrée : chaîne ISO 8601 (le contrat éditorial garantit ce format
 *     côté API, voir `ISO_DATE_PATTERN` dans `editorial-contract.ts`) ;
 *   - sortie : « j mois yyyy » en fr-FR, calé sur `Europe/Paris` ;
 *   - fallback : si l'ISO est invalide, on retourne la chaîne d'origine
 *     (jamais `"Invalid Date"`, jamais d'exception qui casserait le rendu).
 *
 * Pure, sans dépendance à Vue/Nuxt, testable en isolation.
 */

const EDITORIAL_DATE_FORMATTER = new Intl.DateTimeFormat("fr-FR", {
  day: "numeric",
  month: "long",
  year: "numeric",
  timeZone: "Europe/Paris",
})

export function formatEditorialDate(iso: string): string {
  const date = new Date(iso)
  if (Number.isNaN(date.getTime())) return iso
  return EDITORIAL_DATE_FORMATTER.format(date)
}
