/**
 * Composable Nuxt du domaine éditorial.
 *
 * Intègre le service `editorial-api` (via l'endpoint interne Nitro
 * `/_editorial/*`) avec le cycle de vie Nuxt (`useAsyncData` + gestion
 * d'erreurs typée). Les pages consommatrices n'ont plus qu'à afficher.
 *
 * Contrat d'erreur (harmonisé avec la correction obligatoire 6) :
 *   - `data` non-null / `error` null    → contenu à afficher ;
 *   - 404 (upstream ou hors bornes)    → `createError(404)` fatal → 404 SSR ;
 *   - 502 (payload invalide amont)     → `createError(502)` fatal ;
 *   - 503 (indisponible amont)         → `createError(503)` fatal.
 *
 * En navigation SPA client, on utilise le même endpoint : Nitro renvoie
 * les mêmes codes HTTP, le composable les remonte à Nuxt qui déclenche
 * la page d'erreur cohérente avec le SSR.
 */

import type { ComputedRef, MaybeRefOrGetter } from "vue"
import { computed, toValue, watch } from "vue"
import type { ArticleDetail, ArticleListResult, Pagination } from "~/types/editorial"

export interface UseResourceListArgs {
  page: MaybeRefOrGetter<number>
  perPage: MaybeRefOrGetter<number>
  /**
   * Filtre optionnel par identifiant d'expertise. Doit appartenir à
   * `EXPERTISE_IDS` — le proxy Nitro renvoie 400 sur une valeur inconnue,
   * qui se propage ici en 503 fatal (défensif : la page appelante refuse
   * déjà les valeurs hors allowlist avant d'appeler ce composable).
   */
  expertise?: MaybeRefOrGetter<string | null>
}

interface EditorialErrorPayload {
  statusCode?: number
  statusMessage?: string
}

function toStatusCode(error: unknown): number | null {
  if (!error || typeof error !== "object") return null
  const payload = error as EditorialErrorPayload
  return typeof payload.statusCode === "number" ? payload.statusCode : null
}

/**
 * Relève une erreur applicative typée à partir du statut renvoyé par
 * l'endpoint Nitro interne. Fatale → interrompt le rendu de la page
 * courante et déclenche le layout d'erreur avec le bon code HTTP.
 */
function asFatalError(error: unknown, contextLabel: string) {
  const code = toStatusCode(error)
  if (code === 404) {
    return createError({
      statusCode: 404,
      statusMessage: `${contextLabel} introuvable`,
      fatal: true,
    })
  }
  if (code === 502) {
    return createError({
      statusCode: 502,
      statusMessage: `${contextLabel} — payload amont invalide`,
      fatal: true,
    })
  }
  // Toute autre situation (réseau, 5xx, timeout) → 503 côté Nuxt.
  return createError({
    statusCode: 503,
    statusMessage: `${contextLabel} — service indisponible`,
    fatal: true,
  })
}

function rethrowAsFatal(error: unknown, contextLabel: string): never {
  throw asFatalError(error, contextLabel)
}

export interface UseResourceListReturn {
  items: ComputedRef<ArticleListResult["items"]>
  pagination: ComputedRef<Pagination>
  pending: ComputedRef<boolean>
}

/**
 * Charge une page de la liste des ressources publiées.
 *
 * Refuse une pagination hors bornes (404 amont) et propage le 503 si l'API
 * est indisponible. Utilise une clé `useAsyncData` déterministe pour
 * l'hydratation (`resources:list:<page>:<perPage>:<expertise ?? "-">`) —
 * le filtre expertise est intégré à la clé pour éviter qu'une hydratation
 * filtrée écrase la liste globale (ou l'inverse).
 */
export async function useResourceList(args: UseResourceListArgs): Promise<UseResourceListReturn> {
  const page = computed(() => toValue(args.page))
  const perPage = computed(() => toValue(args.perPage))
  const expertise = computed(() =>
    args.expertise === undefined ? null : toValue(args.expertise),
  )
  const key = computed(
    () =>
      `resources:list:${page.value}:${perPage.value}:${expertise.value ?? "-"}`,
  )
  const { data, error, pending } = await useAsyncData<ArticleListResult>(key, () =>
    $fetch<ArticleListResult>("/_editorial/list", {
      query: {
        page: page.value,
        per_page: perPage.value,
        ...(expertise.value ? { expertise: expertise.value } : {}),
      },
    }),
  )

  if (error.value || !data.value) {
    rethrowAsFatal(error.value, "Liste des ressources")
  }

  // Une erreur lors d'une navigation SPA (ex. page devenue hors bornes)
  // doit suivre le même contrat que le premier rendu SSR.
  watch(error, (currentError) => {
    if (currentError) {
      showError(asFatalError(currentError, "Liste des ressources"))
    }
  })

  return {
    items: computed(() => data.value?.items ?? []),
    pagination: computed(
      () =>
        data.value?.pagination ?? {
          page: page.value,
          perPage: perPage.value,
          total: 0,
          totalPages: 0,
        },
    ),
    pending: computed(() => pending.value),
  }
}

export interface UseResourceDetailReturn {
  article: ComputedRef<ArticleDetail>
}

/**
 * Charge le détail d'une ressource par slug.
 *
 * 404 upstream → 404 fatale sur la page (déclenchant le layout d'erreur
 * Nuxt et le bon code HTTP côté SSR).
 */
export async function useResourceDetail(slug: string): Promise<UseResourceDetailReturn> {
  const key = `resources:detail:${slug}`
  const { data, error } = await useAsyncData<ArticleDetail>(key, () =>
    $fetch<ArticleDetail>(`/_editorial/detail/${encodeURIComponent(slug)}`),
  )

  if (error.value || !data.value) {
    rethrowAsFatal(error.value, "Ressource")
  }

  const resolved = data.value as ArticleDetail

  return {
    article: computed(() => resolved),
  }
}
